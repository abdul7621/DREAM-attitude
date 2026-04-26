<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Wholesale Page
        DB::table('pages')->where('slug', 'like', '%wholesale%')->update([
            'content' => '<div class="sf-page-container" style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    <!-- Hero -->
    <div style="text-align: center; margin-bottom: 40px; background: var(--color-plum); padding: 40px 20px; border-radius: 20px; color: white;">
        <h1 style="color: var(--color-gold); font-size: clamp(2em, 5vw, 2.8em); margin-bottom: 15px; font-weight: 700; font-family: \'Playfair Display\', serif;">Become a Dream Attitude Wholesale Partner</h1>
        <p style="font-size: clamp(1em, 3vw, 1.2em); color: rgba(255,255,255,0.9); line-height: 1.6; max-width: 700px; margin: 0 auto 30px auto;">Join India\'s fastest-growing premium beauty & salon brand. Grow your retail business with our high-demand products and marketing support.</p>
        <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="https://wa.me/919974767866" target="_blank" style="display: inline-flex; align-items: center; padding: 12px 24px; background: #25d366; color: white; text-decoration: none; border-radius: 100px; font-weight: bold; transition: opacity 0.3s;"><i class="bi bi-whatsapp me-2"></i> WhatsApp Inquiry</a>
            <a href="mailto:dreamattitudeinternational@gmail.com" style="display: inline-flex; align-items: center; padding: 12px 24px; background: rgba(255,255,255,0.1); color: white; text-decoration: none; border-radius: 100px; border: 1px solid rgba(255,255,255,0.2); font-weight: bold;"><i class="bi bi-envelope me-2"></i> Email Wholesale Desk</a>
        </div>
    </div>

    <!-- Who & Why -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: var(--color-bg-surface); padding: 25px; border-radius: 16px; border: 1px solid var(--color-border);">
            <h3 style="color: var(--color-text-primary); font-size: 1.3em; margin-bottom: 15px;"><i class="bi bi-people me-2" style="color: var(--color-plum);"></i> Who Can Apply?</h3>
            <ul style="color: var(--color-text-muted); padding-left: 20px; line-height: 1.8; margin: 0;">
                <li>Salon Owners & Beauty Parlours</li>
                <li>Retail Cosmetics Stores</li>
                <li>Independent Distributors</li>
                <li>Beauty Entrepreneurs</li>
            </ul>
        </div>
        <div style="background: var(--color-bg-surface); padding: 25px; border-radius: 16px; border: 1px solid var(--color-border);">
            <h3 style="color: var(--color-text-primary); font-size: 1.3em; margin-bottom: 15px;"><i class="bi bi-stars me-2" style="color: var(--color-plum);"></i> Why Partner With Us?</h3>
            <ul style="color: var(--color-text-muted); padding-left: 20px; line-height: 1.8; margin: 0;">
                <li>High-margin premium product range</li>
                <li>Dedicated local influencer marketing</li>
                <li>Facebook Ads targeted to your shop</li>
                <li>Exceptional brand recognition & trust</li>
            </ul>
        </div>
    </div>

    <!-- Cards -->
    <h2 style="text-align: center; margin-bottom: 30px; font-size: clamp(1.8em, 4vw, 2.2em); color: var(--color-text-primary); font-family: \'Playfair Display\', serif;">Program Guidelines & Terms</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="font-size: 2em; margin-bottom: 10px;">💰</div>
            <h4 style="font-size: 1.1em; color: var(--color-text-primary); margin-bottom: 10px;">Payment Terms</h4>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin-bottom: 5px;">• 50% advance payment required to place the order.</p>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">• Remaining 50% must be settled within 30 days of product delivery.</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="font-size: 2em; margin-bottom: 10px;">📦</div>
            <h4 style="font-size: 1.1em; color: var(--color-text-primary); margin-bottom: 10px;">MOQ & Ordering</h4>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">• Each product must be ordered with a Minimum Order Quantity (MOQ) of 12 pieces.</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="font-size: 2em; margin-bottom: 10px;">📄</div>
            <h4 style="font-size: 1.1em; color: var(--color-text-primary); margin-bottom: 10px;">KYC / Documents</h4>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin-bottom: 5px;">• Copy of GST certificate and Aadhaar card.</p>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">• Proof of shop ownership or active rental agreement.</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="font-size: 2em; margin-bottom: 10px;">📢</div>
            <h4 style="font-size: 1.1em; color: var(--color-text-primary); margin-bottom: 10px;">Marketing Support</h4>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin-bottom: 5px;">• You must collaborate with a local influencer for a promotional video at your shop.</p>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">• We will use this video to run targeted Facebook Ads to drive foot traffic to you.</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="font-size: 2em; margin-bottom: 10px;">⚖️</div>
            <h4 style="font-size: 1.1em; color: var(--color-text-primary); margin-bottom: 10px;">Policies & Compliance</h4>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin-bottom: 5px;">• Defaulting on the 30-day payment will halt future orders.</p>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">• We reserve the right to use the influencer video content across our digital platforms.</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: center; text-align: center; flex-direction: column;">
            <div style="font-size: 2.5em; color: var(--color-plum); margin-bottom: 10px;"><i class="bi bi-handshake"></i></div>
            <h4 style="font-size: 1.1em; color: var(--color-text-primary);">Join The Network</h4>
            <a href="https://wa.me/919974767866" target="_blank" style="color: var(--color-plum); font-weight: bold; text-decoration: none;">Apply Now →</a>
        </div>
    </div>
</div>'
        ]);

        // 2. Money Back
        DB::table('pages')->where('slug', 'like', '%money-back%')->update([
            'content' => '<div class="sf-page-container" style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: var(--color-text-primary); font-size: clamp(2em, 5vw, 2.8em); margin-bottom: 15px; font-weight: 700; font-family: \'Playfair Display\', serif;">Shop With Confidence<br><span style="color: var(--color-plum);">100% Money Back Guarantee</span></h1>
        <p style="font-size: clamp(1em, 3vw, 1.1em); color: var(--color-text-muted); line-height: 1.6; max-width: 700px; margin: 0 auto;">We stand by our products and want you to be completely satisfied. If you are not happy with your order, we offer a hassle-free money-back guarantee.</p>
    </div>

    <!-- 3 Trust Blocks -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: var(--color-bg-surface); padding: 25px 20px; border-radius: 16px; border: 1px solid var(--color-border); text-align: center;">
            <div style="font-size: 2.5em; color: var(--color-gold); margin-bottom: 15px;"><i class="bi bi-arrow-return-left"></i></div>
            <h3 style="font-size: 1.1em; margin-bottom: 10px; color: var(--color-text-primary);">7 Day Easy Returns</h3>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">Request a refund within 7 days of delivery if the product is unused and in original packaging.</p>
        </div>
        <div style="background: var(--color-bg-surface); padding: 25px 20px; border-radius: 16px; border: 1px solid var(--color-border); text-align: center;">
            <div style="font-size: 2.5em; color: var(--color-gold); margin-bottom: 15px;"><i class="bi bi-wallet2"></i></div>
            <h3 style="font-size: 1.1em; margin-bottom: 10px; color: var(--color-text-primary);">Fast Refund Processing</h3>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">Refunds are processed swiftly within 5-7 business days after receiving the returned item.</p>
        </div>
        <div style="background: var(--color-bg-surface); padding: 25px 20px; border-radius: 16px; border: 1px solid var(--color-border); text-align: center;">
            <div style="font-size: 2.5em; color: var(--color-gold); margin-bottom: 15px;"><i class="bi bi-headset"></i></div>
            <h3 style="font-size: 1.1em; margin-bottom: 10px; color: var(--color-text-primary);">Customer Support Assistance</h3>
            <p style="color: var(--color-text-muted); font-size: 0.9em; margin: 0;">Damaged or defective? Contact us with photos for an immediate and quick resolution.</p>
        </div>
    </div>

    <!-- Timeline -->
    <div style="background: var(--color-bg-surface); padding: 30px 20px; border-radius: 20px; border: 1px solid var(--color-border); margin-bottom: 40px;">
        <h2 style="text-align: center; margin-bottom: 30px; font-size: clamp(1.5em, 4vw, 2em); color: var(--color-text-primary); font-family: \'Playfair Display\', serif;">How The Refund Process Works</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; position: relative;">
            <div style="text-align: center; position: relative; z-index: 1;">
                <div style="width: 40px; height: 40px; background: var(--color-plum); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1em; font-weight: bold; margin: 0 auto 15px auto;">1</div>
                <h4 style="color: var(--color-text-primary); margin-bottom: 8px; font-size: 1em;">Initiate Return</h4>
                <p style="color: var(--color-text-muted); font-size: 0.85em;">Contact our support team with your order details and reason for return.</p>
            </div>
            <div style="text-align: center; position: relative; z-index: 1;">
                <div style="width: 40px; height: 40px; background: var(--color-plum); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1em; font-weight: bold; margin: 0 auto 15px auto;">2</div>
                <h4 style="color: var(--color-text-primary); margin-bottom: 8px; font-size: 1em;">Ship Item Back</h4>
                <p style="color: var(--color-text-muted); font-size: 0.85em;">Send the unused product back to us. (Return shipping covered by customer).</p>
            </div>
            <div style="text-align: center; position: relative; z-index: 1;">
                <div style="width: 40px; height: 40px; background: var(--color-plum); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1em; font-weight: bold; margin: 0 auto 15px auto;">3</div>
                <h4 style="color: var(--color-text-primary); margin-bottom: 8px; font-size: 1em;">Receive Refund</h4>
                <p style="color: var(--color-text-muted); font-size: 0.85em;">Amount credited to your original payment method in 5-7 days.</p>
            </div>
        </div>
    </div>

    <!-- Bottom Trust Strip & CTA -->
    <div style="background: var(--color-plum); padding: 30px 20px; border-radius: 16px; text-align: center; color: white;">
        <h3 style="color: var(--color-gold); margin-bottom: 20px; font-family: \'Playfair Display\', serif; font-size: clamp(1.5em, 4vw, 1.8em);">Our Commitment To You</h3>
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 25px;">
            <div style="display: flex; align-items: center; gap: 8px;"><i class="bi bi-shield-lock" style="font-size: 1.3em; color: var(--color-gold);"></i> <span style="font-size: 0.9em;">Secure Payments</span></div>
            <div style="display: flex; align-items: center; gap: 8px;"><i class="bi bi-patch-check" style="font-size: 1.3em; color: var(--color-gold);"></i> <span style="font-size: 0.9em;">Quality Assured</span></div>
            <div style="display: flex; align-items: center; gap: 8px;"><i class="bi bi-emoji-smile" style="font-size: 1.3em; color: var(--color-gold);"></i> <span style="font-size: 0.9em;">Satisfaction Promise</span></div>
        </div>
        <a href="mailto:dreamattitudeinternational@gmail.com" style="display: inline-flex; align-items: center; padding: 12px 28px; background: var(--color-gold); color: var(--color-plum); text-decoration: none; border-radius: 100px; font-weight: bold; font-size: 1em; transition: opacity 0.3s;"><i class="bi bi-envelope me-2"></i> Contact Support</a>
    </div>
</div>'
        ]);

        // 3. Connect Us
        DB::table('pages')->where('slug', 'like', '%connect%')->update([
            'content' => '<div class="sf-page-container sf-connect-page" style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: var(--color-text-primary); font-size: clamp(2em, 5vw, 2.8em); margin-bottom: 15px; font-weight: 700; font-family: \'Playfair Display\', serif;">Connect With Dream Attitude</h1>
        <p style="font-size: clamp(1em, 3vw, 1.1em); color: var(--color-text-muted); line-height: 1.6; max-width: 600px; margin: 0 auto;">
            Join us on our journey to make dreams come true. Follow us across platforms for inspiration, updates, and community.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 40px;">
        <a href="https://youtube.com/@dreamattitudeinternational" target="_blank" style="text-decoration: none; display: flex; align-items: center; padding: 20px; background: var(--color-bg-surface); border-radius: 16px; border: 1px solid var(--color-border); transition: all 0.3s ease;">
            <div style="width: 50px; height: 50px; background: rgba(255,0,0,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <i class="bi bi-youtube" style="font-size: 24px; color: #ff0000;"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.1em; color: var(--color-text-primary);">YouTube</h3>
                <p style="margin: 0; color: var(--color-text-muted); font-size: 0.85em;">Video Content & Tutorials</p>
            </div>
            <i class="bi bi-arrow-right" style="margin-left: auto; color: var(--color-text-muted);"></i>
        </a>

        <a href="https://www.instagram.com/dream_attitude_international" target="_blank" style="text-decoration: none; display: flex; align-items: center; padding: 20px; background: var(--color-bg-surface); border-radius: 16px; border: 1px solid var(--color-border); transition: all 0.3s ease;">
            <div style="width: 50px; height: 50px; background: rgba(228,64,95,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <i class="bi bi-instagram" style="font-size: 24px; color: #e4405f;"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.1em; color: var(--color-text-primary);">Instagram</h3>
                <p style="margin: 0; color: var(--color-text-muted); font-size: 0.85em;">Photos & Stories</p>
            </div>
            <i class="bi bi-arrow-right" style="margin-left: auto; color: var(--color-text-muted);"></i>
        </a>

        <a href="https://www.facebook.com/share/1B7UEKVfNH/" target="_blank" style="text-decoration: none; display: flex; align-items: center; padding: 20px; background: var(--color-bg-surface); border-radius: 16px; border: 1px solid var(--color-border); transition: all 0.3s ease;">
            <div style="width: 50px; height: 50px; background: rgba(24,119,242,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <i class="bi bi-facebook" style="font-size: 24px; color: #1877f2;"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.1em; color: var(--color-text-primary);">Facebook</h3>
                <p style="margin: 0; color: var(--color-text-muted); font-size: 0.85em;">Community & Updates</p>
            </div>
            <i class="bi bi-arrow-right" style="margin-left: auto; color: var(--color-text-muted);"></i>
        </a>

        <a href="https://wa.me/917096206785" target="_blank" style="text-decoration: none; display: flex; align-items: center; padding: 20px; background: var(--color-bg-surface); border-radius: 16px; border: 1px solid var(--color-border); transition: all 0.3s ease;">
            <div style="width: 50px; height: 50px; background: rgba(37,211,102,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <i class="bi bi-whatsapp" style="font-size: 24px; color: #25d366;"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.1em; color: var(--color-text-primary);">WhatsApp</h3>
                <p style="margin: 0; color: var(--color-text-muted); font-size: 0.85em;">Direct Messaging</p>
            </div>
            <i class="bi bi-arrow-right" style="margin-left: auto; color: var(--color-text-muted);"></i>
        </a>

        <a href="https://g.page/r/CdVnzzU7vHovEBE/review" target="_blank" style="text-decoration: none; display: flex; align-items: center; padding: 20px; background: var(--color-bg-surface); border-radius: 16px; border: 1px solid var(--color-border); transition: all 0.3s ease;">
            <div style="width: 50px; height: 50px; background: rgba(66,133,244,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <i class="bi bi-google" style="font-size: 24px; color: #4285f4;"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.1em; color: var(--color-text-primary);">Google</h3>
                <p style="margin: 0; color: var(--color-text-muted); font-size: 0.85em;">Rate & Review Us</p>
            </div>
            <i class="bi bi-arrow-right" style="margin-left: auto; color: var(--color-text-muted);"></i>
        </a>
    </div>

    <div style="background: var(--color-bg-surface); padding: 30px 20px; border-radius: 20px; text-align: center; margin-bottom: 40px; border: 1px solid var(--color-border);">
        <h2 style="margin: 0 0 16px 0; font-size: clamp(1.5em, 4vw, 2em); color: var(--color-text-primary); font-family: \'Playfair Display\', serif;">About Dream Attitude</h2>
        <p style="font-size: clamp(0.95em, 3vw, 1.1em); line-height: 1.6; color: var(--color-text-muted); max-width: 700px; margin: 0 auto 30px auto;">
            We are more than just a brand; we are an inspirational movement. We share motivational content, lifestyle tips, and success stories to fuel your journey.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
            <div style="text-align: center;">
                <div style="font-size: 1.8em; margin-bottom: 8px; color: var(--color-gold);"><i class="bi bi-bullseye"></i></div>
                <h4 style="margin: 0; font-size: 1.1em; color: var(--color-text-primary);">Mission</h4>
                <p style="margin: 4px 0 0 0; color: var(--color-text-muted); font-size: 0.85em;">Making Dreams Reality</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 1.8em; margin-bottom: 8px; color: var(--color-gold);"><i class="bi bi-lightbulb"></i></div>
                <h4 style="margin: 0; font-size: 1.1em; color: var(--color-text-primary);">Vision</h4>
                <p style="margin: 4px 0 0 0; color: var(--color-text-muted); font-size: 0.85em;">Source of Inspiration</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 1.8em; margin-bottom: 8px; color: var(--color-gold);"><i class="bi bi-heart"></i></div>
                <h4 style="margin: 0; font-size: 1.1em; color: var(--color-text-primary);">Values</h4>
                <p style="margin: 4px 0 0 0; color: var(--color-text-muted); font-size: 0.85em;">Truth & Inspiration</p>
            </div>
        </div>
    </div>

    <div style="background: var(--color-plum); padding: 30px 20px; border-radius: 16px; text-align: center;">
        <h3 style="color: var(--color-gold); margin: 0 0 10px 0; font-size: clamp(1.5em, 4vw, 1.8em); font-family: \'Playfair Display\', serif;">Get In Touch</h3>
        <p style="color: rgba(255,255,255,0.9); margin: 0 0 24px 0; font-size: 1em;">Have questions or want to collaborate? Reach out to us.</p>
        <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="mailto:dreamattitudeinternational@gmail.com" style="display: inline-flex; align-items: center; padding: 12px 24px; background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; border-radius: 100px; border: 1px solid rgba(255,255,255,0.2);"><i class="bi bi-envelope me-2"></i> Email Us</a>
            <a href="tel:+917096206785" style="display: inline-flex; align-items: center; padding: 12px 24px; background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; border-radius: 100px; border: 1px solid rgba(255,255,255,0.2);"><i class="bi bi-telephone me-2"></i> Call Us</a>
        </div>
    </div>
</div>'
        ]);

        // 4. About Us
        DB::table('pages')->where('slug', 'like', '%about%')->update([
            'content' => '<div class="sf-page-container sf-about-page">
    <!-- Hero -->
    <div style="background: var(--color-plum); padding: 50px 20px 40px 20px; text-align: center; color: white; margin-bottom: 40px;">
        <h1 style="color: var(--color-gold); font-size: clamp(2em, 5vw, 3em); margin-bottom: 15px; font-weight: 700; font-family: \'Playfair Display\', serif;">Beauty Backed By Trust, Innovation & Results</h1>
        <p style="font-size: clamp(1em, 3vw, 1.2em); color: rgba(255,255,255,0.9); line-height: 1.6; max-width: 800px; margin: 0 auto;">Trusted by salons, resellers and thousands of customers across India.</p>
    </div>

    <div style="max-width: 1000px; margin: 0 auto; padding: 0 20px 40px 20px;">
        <!-- Mission / Vision -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 50px;">
            <div>
                <h3 style="color: var(--color-plum); font-size: 1.5em; margin-bottom: 15px; font-family: \'Playfair Display\', serif;"><i class="bi bi-bullseye me-2"></i> Our Mission</h3>
                <p style="color: var(--color-text-muted); font-size: 1em; line-height: 1.6;">To empower individuals and professionals by delivering premium, result-oriented beauty and personal care products. We strive to make luxury beauty accessible while maintaining the highest standards of quality.</p>
            </div>
            <div>
                <h3 style="color: var(--color-plum); font-size: 1.5em; margin-bottom: 15px; font-family: \'Playfair Display\', serif;"><i class="bi bi-eye me-2"></i> Our Vision</h3>
                <p style="color: var(--color-text-muted); font-size: 1em; line-height: 1.6;">To be India\'s most trusted D2C beauty partner, recognized not just for our exceptional products, but for the confidence and attitude we inspire in our community every single day.</p>
            </div>
        </div>

        <!-- Award Badge Section -->
        <div style="background: var(--color-bg-surface); border: 2px solid var(--color-gold); border-radius: 20px; padding: 30px 20px; text-align: center; margin-bottom: 50px; display: flex; flex-direction: column; align-items: center;">
            <div style="font-size: 2.5em; color: var(--color-gold); margin-bottom: 15px;"><i class="bi bi-trophy-fill"></i></div>
            <h2 style="color: var(--color-text-primary); font-size: clamp(1.5em, 4vw, 2.2em); margin-bottom: 15px; font-family: \'Playfair Display\', serif;">Asian Excellence Award 2021</h2>
            <p style="color: var(--color-text-muted); font-size: 1em; max-width: 600px; margin: 0 auto 20px auto;">Proudly recognized for our commitment to quality and innovation in the beauty industry.</p>
            
            <div style="width: 100%; max-width: 400px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <img src="/placeholder-award.jpg" alt="Asian Excellence Award" style="width: 100%; height: auto; display: block;" onerror="this.src=\'https://via.placeholder.com/800x800.png?text=Click+Here+To+Change+Image\'">
            </div>
        </div>

        <!-- Why Customers Trust Us -->
        <h2 style="text-align: center; margin-bottom: 30px; font-size: clamp(1.8em, 4vw, 2em); color: var(--color-text-primary); font-family: \'Playfair Display\', serif;">Why Customers Trust Us</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 40px; text-align: center;">
            <div>
                <div style="width: 60px; height: 60px; background: rgba(var(--color-plum-rgb), 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8em; color: var(--color-plum); margin: 0 auto 10px auto;"><i class="bi bi-patch-check"></i></div>
                <h4 style="color: var(--color-text-primary); margin-bottom: 8px; font-size: 1.1em;">Premium Quality</h4>
                <p style="color: var(--color-text-muted); font-size: 0.85em;">Expertly formulated with top-tier ingredients.</p>
            </div>
            <div>
                <div style="width: 60px; height: 60px; background: rgba(var(--color-plum-rgb), 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8em; color: var(--color-plum); margin: 0 auto 10px auto;"><i class="bi bi-truck"></i></div>
                <h4 style="color: var(--color-text-primary); margin-bottom: 8px; font-size: 1.1em;">PAN India Delivery</h4>
                <p style="color: var(--color-text-muted); font-size: 0.85em;">Fast and secure shipping across the country.</p>
            </div>
            <div>
                <div style="width: 60px; height: 60px; background: rgba(var(--color-plum-rgb), 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8em; color: var(--color-plum); margin: 0 auto 10px auto;"><i class="bi bi-people"></i></div>
                <h4 style="color: var(--color-text-primary); margin-bottom: 8px; font-size: 1.1em;">Salon Approved</h4>
                <p style="color: var(--color-text-muted); font-size: 0.85em;">Trusted and used by industry professionals.</p>
            </div>
        </div>

        <!-- CTA -->
        <div style="text-align: center;">
            <a href="/search" style="display: inline-flex; align-items: center; padding: 14px 32px; background: var(--color-plum); color: white; text-decoration: none; border-radius: 100px; font-weight: bold; font-size: 1em; transition: background 0.3s;"><i class="bi bi-bag-check me-2"></i> Shop Collection</a>
        </div>
    </div>
</div>'
        ]);
    }

    public function down(): void
    {
        // Revert not needed for content update.
    }
};
